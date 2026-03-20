import { createHash } from 'node:crypto';
import { build, context } from 'esbuild';
import { readFile, readdir, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';

const themeRoot = process.cwd();
const distRoot = path.join(themeRoot, 'dist');
const isWatch = process.argv.includes('--watch');
const manifestPath = path.join(distRoot, 'asset-manifest.json');

async function collectEntries(directory, extension) {
  const entries = [];
  let dirEntries = [];

  try {
    dirEntries = await readdir(directory, { withFileTypes: true });
  } catch {
    return entries;
  }

  for (const dirent of dirEntries) {
    const resolved = path.join(directory, dirent.name);

    if (dirent.isDirectory()) {
      entries.push(...await collectEntries(resolved, extension));
      continue;
    }

    if (dirent.isFile() && resolved.endsWith(extension)) {
      entries.push(resolved);
    }
  }

  return entries;
}

async function getEntryPoints() {
  const cssEntries = await collectEntries(path.join(themeRoot, 'assets', 'css'), '.css');
  const jsEntries = await collectEntries(path.join(themeRoot, 'assets', 'js'), '.js');
  const rootStyle = path.join(themeRoot, 'style.css');

  return [rootStyle, ...cssEntries, ...jsEntries];
}

async function cleanDist() {
  await rm(distRoot, { recursive: true, force: true });
}

function toPosixPath(filePath) {
  return filePath.split(path.sep).join('/');
}

async function collectBuiltFiles(directory) {
  const entries = [];
  let dirEntries = [];

  try {
    dirEntries = await readdir(directory, { withFileTypes: true });
  } catch {
    return entries;
  }

  for (const dirent of dirEntries) {
    const resolved = path.join(directory, dirent.name);

    if (dirent.isDirectory()) {
      entries.push(...await collectBuiltFiles(resolved));
      continue;
    }

    if (dirent.isFile() && resolved !== manifestPath) {
      entries.push(resolved);
    }
  }

  return entries;
}

async function writeManifest() {
  const builtFiles = await collectBuiltFiles(distRoot);
  const manifest = {};

  for (const filePath of builtFiles) {
    const relativePath = toPosixPath(path.relative(distRoot, filePath));
    const content = await readFile(filePath);
    const hash = createHash('sha1').update(content).digest('hex').slice(0, 12);

    manifest[relativePath] = {
      path: relativePath,
      version: hash
    };
  }

  await writeFile(manifestPath, JSON.stringify(manifest, null, 2));
}

async function run() {
  const entryPoints = await getEntryPoints();

  const options = {
    entryPoints,
    outdir: distRoot,
    outbase: themeRoot,
    bundle: false,
    minify: true,
    sourcemap: false,
    legalComments: 'none',
    charset: 'utf8',
    target: ['es2018'],
    logLevel: 'info',
    plugins: [
      {
        name: 'asset-manifest',
        setup(buildInstance) {
          buildInstance.onEnd(async (result) => {
            if (result.errors.length > 0) {
              return;
            }

            await writeManifest();
          });
        }
      }
    ]
  };

  await cleanDist();

  if (isWatch) {
    const ctx = await context(options);
    await ctx.watch();
    console.log('Watching assets and writing minified files to dist/...');
    return;
  }

  await build(options);
}

run().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});